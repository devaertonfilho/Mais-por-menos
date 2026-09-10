import { Routes } from '@angular/router';
import { NovaListaPage } from './pages/nova-lista/nova-lista.page';

export const routes: Routes = [
  { path: 'nova-lista', component: NovaListaPage },
  { path: '', pathMatch: 'full', redirectTo: 'nova-lista' },
  { path: '**', redirectTo: 'nova-lista' }
];
