import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

const source = readFileSync(new URL('../../assets/controllers/admin_dashboard_controller.js', import.meta.url), 'utf8')
  .replace('import { Controller } from "@hotwired/stimulus";', '')
  .replace('export default class extends Controller', 'globalThis.Dashboard = class extends Controller');

function setup({ confirmed = true, status = 204 } = {}) {
  const requests = [];
  const context = vm.createContext({
    Controller: class {},
    window: { confirm: () => confirmed, location: {} },
    localStorage: { getItem: () => 'token', removeItem: () => {} },
    fetch: async (url, options) => {
      requests.push({ url, options });
      return { ok: status === 204, status, json: async () => ({ message: 'Dossier lié' }) };
    },
  });
  vm.runInContext(source, context);
  const dashboard = new context.Dashboard();
  Object.assign(dashboard, {
    agentsUrlValue: '/api/admin/agents',
    sellersUrlValue: '/api/admin/sellers',
    vehiclesUrlValue: '/api/admin/vehicles/',
    pages: { agents: 2, sellers: 2, vehicles: 2 },
    feedbackTarget: {},
    reloads: 0,
    async loadAll() { this.reloads++; },
  });
  const button = {
    dataset: { kind: 'vehicles', id: '42', label: 'AA-112-AA' },
    disabled: false,
    closest: () => ({ rows: [{}] }),
  };
  return { dashboard, button, requests, context };
}

test('annuler la confirmation ne supprime rien', async () => {
  const { dashboard, button, requests } = setup({ confirmed: false });
  await dashboard.deleteRecord({ currentTarget: button });
  assert.equal(requests.length, 0);
  assert.equal(dashboard.reloads, 0);
});

test('une suppression 204 utilise le JWT et recharge les listes', async () => {
  const { dashboard, button, requests } = setup();
  await dashboard.deleteRecord({ currentTarget: button });
  assert.equal(requests[0].url, '/api/admin/vehicles/42');
  assert.equal(requests[0].options.method, 'DELETE');
  assert.equal(requests[0].options.headers.Authorization, 'Bearer token');
  assert.equal(dashboard.reloads, 1);
  assert.equal(button.disabled, false);
});

test('un refus conserve la liste et affiche le message du serveur', async () => {
  const { dashboard, button } = setup({ status: 409 });
  await dashboard.deleteRecord({ currentTarget: button });
  assert.equal(dashboard.reloads, 0);
  assert.equal(dashboard.feedbackTarget.textContent, 'Dossier lié');
  assert.equal(button.disabled, false);
});

test('supprimer le dernier compte de la page revient à la précédente', async () => {
  const { dashboard, button, requests } = setup();
  button.dataset.kind = 'sellers';
  await dashboard.deleteRecord({ currentTarget: button });
  assert.equal(requests[0].url, '/api/admin/sellers/42');
  assert.equal(dashboard.pages.sellers, 1);
});

test('un token expiré renvoie à la connexion', async () => {
  const { dashboard, button, context } = setup({ status: 401 });
  await dashboard.deleteRecord({ currentTarget: button });
  assert.equal(context.window.location.href, '/account');
  assert.equal(dashboard.reloads, 0);
});

function setupCounts(stats) {
  const { dashboard } = setup();
  delete dashboard.loadAll;
  dashboard.agentsTbodyTarget = {};
  dashboard.sellersTbodyTarget = {};
  dashboard.vehiclesTbodyTarget = {};
  dashboard.countUsersTarget = {};
  dashboard.countAgentsTarget = {};
  dashboard.countSellersTarget = {};
  dashboard.countVehiclesTarget = {};
  dashboard.renderAgents = () => {};
  dashboard.renderSellers = () => {};
  dashboard.renderVehicles = () => {};
  dashboard.renderPagination = () => {};
  const responses = [
    { data: [{}], meta: { totalItems: 9 } },
    { data: Array(10).fill({}), meta: { totalItems: 27 } },
    Array(21).fill({}),
    stats,
  ];
  dashboard.safeJson = async () => responses.shift();
  return dashboard;
}

test('les compteurs utilisent les totaux globaux, administrateurs compris', async () => {
  const dashboard = setupCounts({ counts: { users: 42, agents: 9, sellers: 27, vehicles: 21 } });
  await dashboard.loadAll();
  assert.equal(dashboard.countUsersTarget.textContent, '42');
  assert.equal(dashboard.countAgentsTarget.textContent, '9');
  assert.equal(dashboard.countSellersTarget.textContent, '27');
  assert.equal(dashboard.countVehiclesTarget.textContent, '21');
});

test('un échec de chargement des statistiques ne devient pas un faux zéro', async () => {
  const dashboard = setupCounts(null);
  await dashboard.loadAll();
  assert.equal(dashboard.countUsersTarget.textContent, '—');
  assert.equal(dashboard.countAgentsTarget.textContent, '—');
});


test('supprimer le dernier véhicule de la page revient à la précédente', async () => {
  const { dashboard, button } = setup();
  await dashboard.deleteRecord({ currentTarget: button });
  assert.equal(dashboard.pages.vehicles, 1);
});

test('les trois listes demandent 10 lignes sur leur page respective', async () => {
  const dashboard = setupCounts({ counts: {} });
  const urls = [];
  dashboard.safeJson = async (url) => { urls.push(url); return null; };
  await dashboard.loadAll();
  assert.ok(urls.includes('/api/admin/agents?page=2&limit=10'));
  assert.ok(urls.includes('/api/admin/sellers?page=2&limit=10'));
  assert.ok(urls.includes('/api/admin/vehicles/?page=2&limit=10'));
});
