import { Routes } from '@angular/router';
import { HomePage } from './pages/home/home.page';
import { NovaListaPage } from './pages/nova-lista/nova-lista.page';

export const routes: Routes = [
  { path: 'home', component: HomePage },
  { path: 'nova-lista', component: NovaListaPage },
  { path: '', pathMatch: 'full', redirectTo: 'home' },
  { path: '**', redirectTo: 'home' }
];
