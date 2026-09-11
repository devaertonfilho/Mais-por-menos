import { Routes } from '@angular/router';
import { HomePage } from './pages/home/home.page';
import { NovaListaPage } from './pages/nova-lista/nova-lista.page';
import { ListaPage } from './pages/lista/lista.page';
import { ListaDetalhePage } from './pages/lista-detalhe/lista-detalhe.page';
import { CadastroPage } from './pages/cadastro/cadastro.page';
import { LoginPage } from './pages/login/login.page';

export const routes: Routes = [
  { path: 'cadastro', component: CadastroPage },
  { path: 'home', component: HomePage },
  { path: 'login', component: LoginPage },
  { path: 'lista', component: ListaPage },
  { path: 'lista-detalhe/:id', component: ListaDetalhePage },
  { path: 'nova-lista', component: NovaListaPage },
  { path: 'scanner', loadComponent: () => import('./pages/scanner/scanner.page').then(m => m.ScannerPage) },
  { path: '', pathMatch: 'full', redirectTo: 'home' },
  { path: '**', redirectTo: 'home' }
];
