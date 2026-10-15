import { CommonModule } from "@angular/common";
import { Component, inject } from "@angular/core";
import { ActivatedRoute } from "@angular/router";
import { catchError, distinctUntilChanged, filter, map, of, shareReplay, switchMap } from "rxjs";
import { ProductService } from "../../services/product.service";

@Component({
  selector: "app-product-detail-page",
  standalone: true,
  imports: [CommonModule],
  templateUrl: "./product-detail-page.component.html",
})
export class ProductDetailPageComponent {
  private readonly productService = inject(ProductService);
  private readonly route = inject(ActivatedRoute);
  readonly product$ = this.route.paramMap.pipe(
    map((params) => params.get("id")),
    filter((id): id is string => Boolean(id)),
    distinctUntilChanged(),
    switchMap((id) => this.productService.getById(id)),
    catchError(() => of(null)),
    shareReplay({ bufferSize: 1, refCount: true }),
  );
  readonly productError$ = this.product$.pipe(
    map((product) =>
      product ? null : "Не удалось загрузить карточку товара",
    ),
  );
}