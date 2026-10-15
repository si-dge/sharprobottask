export interface Product {
  id: string;
  external_code: string;
  name: string;
  description: string;
  price: number;
  discount: number;
}

export interface ProductAttribute {
  id: number;
  product_id: string;
  key: string;
  value: string;
}

export interface ProductImage {
  id: number;
  product_id: string;
  url: string;
  path: string;
}

export interface ProductDetails extends Product {
  attributes: ProductAttribute[];
  images: ProductImage[];
}

export interface ProductListing {
  items: Product[];
  total: number;
  totalPages: number;
  page: number;
  limit: number;
}
