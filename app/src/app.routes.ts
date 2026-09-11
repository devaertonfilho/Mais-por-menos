import { Routes } from '@angular/router';
import { HomePage } from './pages/home/home.page';
import { NovaListaPage } from './pages/nova-lista/nova-lista.page';
import { CadastroPage } from './pages/cadastro/cadastro.page';
import { LoginPage } from './pages/login/login.page';

export const routes: Routes = [
  { path: 'home', component: HomePage },
  { path: 'cadastro', component: CadastroPage },
  { path: 'login', component: LoginPage },
  { path: 'nova-lista', component: NovaListaPage },
  { path: '', pathMatch: 'full', redirectTo: 'home' },
  { path: '**', redirectTo: 'home' }
];
