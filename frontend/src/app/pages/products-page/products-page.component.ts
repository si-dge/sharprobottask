import { CommonModule } from "@angular/common";
import { Component, inject, OnInit } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { RouterLink } from "@angular/router";
import { Store } from "@ngrx/store";
import { loadProducts } from "../../store/product.actions";
import {
  selectAllProducts,
  selectProductsError,
  selectProductsLoading,
  selectTotalPages,
} from "../../store/product.selectors";

@Component({
  selector: "app-products-page",
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: "./products-page.component.html",
})
export class ProductsPageComponent implements OnInit {
  private readonly store = inject(Store);
  readonly products$ = this.store.select(selectAllProducts);
  readonly loading$ = this.store.select(selectProductsLoading);
  readonly error$ = this.store.select(selectProductsError);
  readonly totalPages$ = this.store.select(selectTotalPages);
  name = "";
  minPrice?: number;
  maxPrice?: number;
  page = 1;

  ngOnInit() {
    this.load();
  }

  load() {
    this.store.dispatch(
      loadProducts({
        page: this.page,
        limit: 20,
        name: this.name || undefined,
        minPrice: this.minPrice,
        maxPrice: this.maxPrice,
      }),
    );
  }
}