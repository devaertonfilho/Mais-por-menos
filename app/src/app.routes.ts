import { Routes } from '@angular/router';
import { NovaListaPage } from './pages/nova-lista/nova-lista.page';
import { ListaPage } from './pages/lista/lista.page';
import { ListaDetalhePage } from './pages/lista-detalhe/lista-detalhe.page';

export const routes: Routes = [
  { path: 'lista', component: ListaPage },
  { path: 'lista-detalhe/:id', component: ListaDetalhePage },
  { path: 'nova-lista', component: NovaListaPage },
  { path: 'scanner', loadComponent: () => import('./app/pages/scanner/scanner.page').then(m => m.ScannerPage) },
  { path: '', pathMatch: 'full', redirectTo: 'lista' },
  { path: '**', redirectTo: 'lista' }
];
