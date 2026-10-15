import { createAction, props } from "@ngrx/store";
import { Product } from "../models/product";

export interface ProductQuery {
  page: number;
  limit: number;
  name?: string;
  minPrice?: number;
  maxPrice?: number;
}

export const loadProducts = createAction(
  "[Product] Load Products",
  props<ProductQuery>(),
);

export const loadProductsSuccess = createAction(
  "[Product] Load Products Success",
  props<{ products: Product[]; totalPages: number; page: number }>(),
);

export const loadProductsFailure = createAction(
  "[Product] Load Products Failure",
  props<{ error: string }>(),
);