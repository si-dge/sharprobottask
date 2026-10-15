import { HttpClient, HttpParams } from "@angular/common/http";
import { Injectable, inject } from "@angular/core";
import { Observable } from "rxjs";
import { ProductDetails, ProductListing } from "../models/product";

export interface ProductFilters {
  page: number;
  limit: number;
  name?: string;
  minPrice?: number;
  maxPrice?: number;
}

@Injectable({ providedIn: "root" })
export class ProductService {
  private readonly http = inject(HttpClient);

  list(filters: ProductFilters): Observable<ProductListing> {
    let params = new HttpParams()
      .set("page", filters.page)
      .set("limit", filters.limit);

    if (filters.name) {
      params = params.set("name", filters.name);
    }

    if (filters.minPrice !== undefined) {
      params = params.set("minPrice", filters.minPrice);
    }

    if (filters.maxPrice !== undefined) {
      params = params.set("maxPrice", filters.maxPrice);
    }

    return this.http.get<ProductListing>("/api/products", { params });
  }

  getById(id: string): Observable<ProductDetails> {
    return this.http.get<ProductDetails>(`/api/products/${id}`);
  }
}
