import { Injectable, inject } from "@angular/core";
import { Actions, createEffect, ofType } from "@ngrx/effects";
import { of } from "rxjs";
import { catchError, map, switchMap } from "rxjs/operators";
import * as ProductActions from "./product.actions";
import { ProductService } from "../services/product.service";

@Injectable()
export class ProductEffects {
  private readonly actions$ = inject(Actions);
  private readonly productService = inject(ProductService);

  loadProducts$ = createEffect(() =>
    this.actions$.pipe(
      ofType(ProductActions.loadProducts),
      switchMap((query) =>
        this.productService.list(query).pipe(
          map((result) =>
            ProductActions.loadProductsSuccess({
              products: result.items,
              totalPages: result.totalPages,
              page: result.page,
            }),
          ),
          catchError((error: unknown) =>
            of(
              ProductActions.loadProductsFailure({
                error: error instanceof Error ? error.message : "Не удалось загрузить товары",
              }),
            ),
          ),
        ),
      ),
    ),
  );
}