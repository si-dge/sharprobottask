import { Routes } from "@angular/router";
import { ImportPageComponent } from "./pages/import-page/import-page.component";
import { ProductDetailPageComponent } from "./pages/product-detail-page/product-detail-page.component";
import { ProductsPageComponent } from "./pages/products-page/products-page.component";
import { WorkerTasksPageComponent } from "./pages/worker-tasks-page/worker-tasks-page.component";
import { LoginPageComponent } from "./pages/login-page/login-page.component";
import { authGuard } from "./auth.guard";

export const routes: Routes = [
	{ path: "login", component: LoginPageComponent },
	{ path: "", redirectTo: "products", pathMatch: "full" },
	{ path: "import", component: ImportPageComponent, canActivate: [authGuard] },
	{ path: "products", component: ProductsPageComponent, canActivate: [authGuard] },
	{ path: "products/:id", component: ProductDetailPageComponent, canActivate: [authGuard] },
	{ path: "worker-tasks", component: WorkerTasksPageComponent, canActivate: [authGuard] },
];
