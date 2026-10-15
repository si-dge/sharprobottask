import { Routes } from "@angular/router";
import { authGuard } from "./auth.guard";

export const routes: Routes = [
	{
		path: "login",
		loadComponent: () =>
			import("./pages/login-page/login-page.component").then(
				(module) => module.LoginPageComponent,
			),
	},
	{ path: "", redirectTo: "products", pathMatch: "full" },
	{
		path: "import",
		loadComponent: () =>
			import("./pages/import-page/import-page.component").then(
				(module) => module.ImportPageComponent,
			),
		canActivate: [authGuard],
	},
	{
		path: "products",
		loadComponent: () =>
			import("./pages/products-page/products-page.component").then(
				(module) => module.ProductsPageComponent,
			),
		canActivate: [authGuard],
	},
	{
		path: "products/:id",
		loadComponent: () =>
			import("./pages/product-detail-page/product-detail-page.component").then(
				(module) => module.ProductDetailPageComponent,
			),
		canActivate: [authGuard],
	},
	{
		path: "worker-tasks",
		loadComponent: () =>
			import("./pages/worker-tasks-page/worker-tasks-page.component").then(
				(module) => module.WorkerTasksPageComponent,
			),
		canActivate: [authGuard],
	},
];
