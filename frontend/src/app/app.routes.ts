import { Routes } from "@angular/router";
import { ImportPageComponent } from "./pages/import-page/import-page.component";
import { ProductDetailPageComponent } from "./pages/product-detail-page/product-detail-page.component";
import { ProductsPageComponent } from "./pages/products-page/products-page.component";
import { WorkerTasksPageComponent } from "./pages/worker-tasks-page/worker-tasks-page.component";

export const routes: Routes = [
	{ path: "", redirectTo: "products", pathMatch: "full" },
	{ path: "import", component: ImportPageComponent },
	{ path: "products", component: ProductsPageComponent },
	{ path: "products/:id", component: ProductDetailPageComponent },
	{ path: "worker-tasks", component: WorkerTasksPageComponent },
];
