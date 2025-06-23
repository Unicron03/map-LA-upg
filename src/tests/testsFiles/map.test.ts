import { test, expect } from '@playwright/test';

test.describe("Tests des éléments visuels",  async () => {    
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/map-la-upg/');
  });

  test('Le panneau fonctionne', async ({ page }) => {
    const leafletUI = page.getByText('o +−Leaflet');
    await expect(leafletUI).toBeVisible();
    const head=page.locator('div').filter({ hasText: 'Zelda: Link\'s Awakening' }).nth(1); 
    await expect(head).toBeVisible();

    await page.pause();
  });

  test("Screenshot de la page d'accueil sans connexion", async ({ page }) => {
    await page.screenshot({ path: "screenshots/home_notConnected.png" });
  });
})

test.describe("Tests de connexion avec la BDD",  async () => {    
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/map-la-upg/');
  });

  test('La connexion au compte fonctionne correctement', async ({ page }) => {
    await page.getByRole('button', { name: 'icon-user' }).click();
    await page.getByRole('textbox', { name: 'Email' }).fill('account.example@gmail.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('ilovecats'); 
    await page.getByRole('button', { name: 'Login' }).click();

    await expect(page.getByRole('button', { name: 'Perso Perso' })).toBeVisible();

    await page.pause();
  });

  test('Les marqueurs sont bien récupérés', async ({ page }) => {
    await expect(page.getByTitle('Necklace').getByRole('img')).toBeVisible();

    await page.pause();
  });

  test('Les données des marqueurs sont bien récupérés', async ({ page }) => {
    await page.getByTitle('Necklace').click();

    await expect(page.locator('.popupMarkerImg')).toBeVisible();

    await page.pause();
  });

  test('Les fonctionnalités de connexion ne se font pas or connexion', async ({ page }) => {
    await page.getByTitle('Necklace').click();
    
    await expect(page.getByRole('button', { name: 'Log in to use this feature' }).first()).toBeDisabled();

    await page.pause();
  });

  test("Screenshot de la page d'accueil avec connexion", async ({ page }) => {
    await page.getByRole('button', { name: 'icon-user' }).click();
    await page.getByRole('textbox', { name: 'Email' }).fill('account.example@gmail.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('ilovecats'); 
    await page.getByRole('button', { name: 'Login' }).click();

    await page.pause();

    await page.screenshot({ path: "screenshots/home_connected.png" });
  });

  test('Le marqueur personnalisé persiste après rechargement', async ({ page }) => {
    await page.goto('http://localhost/map-la-upg/');

    // Connexion
    await page.getByRole('button', { name: 'icon-user' }).click();
    await page.getByRole('textbox', { name: 'Email' }).fill('account.example@gmail.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('ilovecats'); 
    await page.getByRole('button', { name: 'Login' }).click();

    // Créer un marqueur
    await page.mouse.click(500, 400, { button: 'right' });
    await page.getByLabel('Title :').fill('Test Marker Persist');
    await page.getByLabel('Description :').fill('Test description');
    await page.getByRole('button', { name: 'Create the marker' }).click();

    // Recharger et attendre le marqueur
    await page.reload({"waitUntil": "networkidle"});
    await expect(page.getByTitle('Test Marker Persist', { exact: true })).toBeVisible();

    await page.getByTitle('Test Marker Persist').click();
    await page.getByRole('button', { name: 'Modify the Marker' }).click();
    await page.getByRole('button', { name: 'Delete the marker' }).click();

    await page.pause();
  });
})

test.describe("Tests des fonctionnalités",  async () => {    
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/map-la-upg/');

    await page.getByRole('button', { name: 'icon-user' }).click();
    await page.getByRole('textbox', { name: 'Email' }).fill('account.example@gmail.com');
    await page.getByRole('textbox', { name: 'Password' }).fill('ilovecats'); 
    await page.getByRole('button', { name: 'Login' }).click();
  });

  test('Le filtre like affiche uniquement les marqueurs likés', async ({ page }) => {
    await page.getByRole('button', { name: 'Select/Deselect All' }).click();
    await page.getByRole('button', { name: /favorites/i }).click();
    await page.getByRole('button', { name: /completed/i }).click();
    await page.getByRole('button', { name: /favorites/i }).click();
    const shownMarkers = await page.locator('.leaflet-marker-icon[style*="display: block"]').count();

    expect(shownMarkers).toBeGreaterThan(0);

    await page.pause();
  });

  test("Destruction d'un marqueur personnel", async ({ page }) => {
    await page.mouse.click(700, 500, { button: 'right' });
    await page.getByLabel('Title :').fill('Test Marker Destruct');
    await page.getByLabel('Description :').fill('Test description');
    await page.getByRole('button', { name: 'Create the marker' }).click();
    
    await page.getByTitle('Test Marker Destruct').click();
    await page.getByRole('button', { name: 'Modify the Marker' }).click();
    await page.getByRole('button', { name: 'Delete the marker' }).click();

    await expect(page.getByTitle('Test Marker Destruct')).toHaveCount(0);

    await page.pause();
  });
})