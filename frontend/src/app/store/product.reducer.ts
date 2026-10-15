import { createReducer, on } from "@ngrx/store";
import { Product } from "../models/product";
import * as ProductActions from "./product.actions";

export interface ProductState {
  products: Product[];
  loading: boolean;
  error: string | null;
  totalPages: number;
  page: number;
}

export const initialState: ProductState = {
  products: [],
  loading: false,
  error: null,
  totalPages: 1,
  page: 1,
};

export const productReducer = createReducer(
  initialState,
  on(ProductActions.loadProducts, (state, { page }) => ({
    ...state,
    loading: true,
    error: null,
    page,
  })),
  on(ProductActions.loadProductsSuccess, (state, { products, totalPages, page }) => ({
    ...state,
    products,
    totalPages,
    page,
    loading: false,
  })),
  on(ProductActions.loadProductsFailure, (state, { error }) => ({
    ...state,
    loading: false,
    error,
  })),
);