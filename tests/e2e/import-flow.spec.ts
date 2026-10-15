import { test, expect } from "@playwright/test";
import * as path from "node:path";

const importFile = path.resolve(__dirname, "../../import_example.xlsx");

test("admin can import products and open a product card", async ({ page }) => {
  test.setTimeout(240_000);

  await page.goto("/login");
  await expect(page).toHaveURL(/\/login$/);

  await page.getByLabel("Логин").fill("admin");
  await page.getByLabel("Пароль").fill("admin");
  await page.getByRole("button", { name: "Войти" }).click();
  await expect(page).toHaveURL(/\/products$/);

  await page.getByRole("link", { name: "Импорт" }).click();
  await expect(page).toHaveURL(/\/import$/);

  await page.locator('input[type="file"]').setInputFiles(importFile);
  await page.getByRole("button", { name: "Запустить импорт" }).click();
  await expect(page.getByText("Импорт поставлен в очередь")).toBeVisible();

  await page.waitForTimeout(120_000);

  await page.getByRole("link", { name: "Товары" }).click();
  await expect(page).toHaveURL(/\/products$/);
  await expect(page.locator("tbody tr").first()).toBeVisible();

  const firstProduct = page.locator("tbody tr a").first();
  await expect(firstProduct).toBeVisible();
  const productName = await firstProduct.innerText();
  await firstProduct.click();

  await expect(page).toHaveURL(/\/products\/[^/]+$/);
  await expect(page.getByRole("heading", { name: productName, exact: true })).toBeVisible();
  await expect(page.getByRole("heading", { name: "Основная информация", exact: true })).toBeVisible();
});
