import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: 'onboarding',
    loadComponent: () => import('./pages/onboarding/onboarding.page').then(m => m.OnboardingPage)
  },
  {
    path: 'cadastro',
    loadComponent: () => import('./pages/cadastro/cadastro.page').then(m => m.CadastroPage)
  },
  {
    path: 'home',
    loadComponent: () => import('./pages/home/home.page').then(m => m.HomePage)
  },
  {
    path: 'login',
    loadComponent: () => import('./pages/login/login.page').then(m => m.LoginPage)
  },
  {
    path: 'lista',
    loadComponent: () => import('./pages/lista/lista.page').then(m => m.ListaPage)
  },
  {
    path: 'lista-detalhe/:id',
    loadComponent: () => import('./pages/lista-detalhe/lista-detalhe.page').then(m => m.ListaDetalhePage)
  },
  {
    path: 'nova-lista',
    loadComponent: () => import('./pages/nova-lista/nova-lista.page').then(m => m.NovaListaPage)
  },
  {
    path: 'scanner',
    loadComponent: () => import('./pages/scanner/scanner.page').then(m => m.ScannerPage)
  },
  { path: '', pathMatch: 'full', redirectTo: 'onboarding' },
  { path: '**', redirectTo: 'onboarding' }
];
