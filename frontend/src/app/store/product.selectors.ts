import { createFeatureSelector, createSelector } from "@ngrx/store";
import { ProductState } from "./product.reducer";

export const selectProductState =
  createFeatureSelector<ProductState>("products");

export const selectAllProducts = createSelector(
  selectProductState,
  (state) => state.products,
);

export const selectProductsLoading = createSelector(
  selectProductState,
  (state) => state.loading,
);

export const selectProductsError = createSelector(
  selectProductState,
  (state) => state.error,
);

export const selectTotalPages = createSelector(
  selectProductState,
  (state) => state.totalPages,
);

export const selectCurrentPage = createSelector(
  selectProductState,
  (state) => state.page,
);