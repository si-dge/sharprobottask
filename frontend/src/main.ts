import { bootstrapApplication } from "@angular/platform-browser";
import { provideHttpClient } from "@angular/common/http";
import { provideRouter } from "@angular/router";
import { provideEffects } from "@ngrx/effects";
import { provideStore } from "@ngrx/store";
import { AppComponent } from "./app/app.component";
import { routes } from "./app/app.routes";
import { ProductEffects } from "./app/store/product.effects";
import { productReducer } from "./app/store/product.reducer";
bootstrapApplication(AppComponent, {
  providers: [
    provideHttpClient(),
    provideRouter(routes),
    provideStore({ products: productReducer }),
    provideEffects([ProductEffects]),
  ],
}).catch(console.error);
