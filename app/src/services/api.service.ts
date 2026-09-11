import { HttpClient, HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, throwError, of } from 'rxjs';
import { catchError, throwError as throwErrorRx } from 'rxjs';
import { catchError as catchErrorRx } from 'rxjs/operators';
import { tap } from 'rxjs/operators';

export interface ApiResponse<T> {
  status: 'sucesso' | 'erro';
  mensagem?: string;
  dados?: T;
}

export interface Produto {
  id: number;
  codigo_barras: string;
  nome: string;
  marca: string | null;
  categoria: string | null;
  peso: string | null;
}

export interface Lista {
  id: number;
  usuario_id: number;
  nome: string;
}

@Injectable({ providedIn: 'root' })
export class ApiService {
  private readonly baseUrl = 'http://localhost:8080';

  constructor(private readonly http: HttpClient) {}

  // --- AUTENTICAÇÃO ---
  login(email: string, senha: string): Observable<ApiResponse<{ token: string, usuario: any }>> {
    return this.http.post<ApiResponse<{ token: string, usuario: any }>>(`${this.baseUrl}/login`, { email, senha });
  }

  // --- LISTAS ---
  criarLista(nome: string): Observable<ApiResponse<Lista>> {
    // MOCK TOTAL: Cria a lista instantaneamente sem servidor
    return of({
      status: 'sucesso',
      mensagem: 'Lista criada com sucesso (Simulado)!',
      dados: { id: Math.floor(Math.random() * 1000), usuario_id: 1, nome: nome }
    });
  }

  listarMinhasListas(): Observable<ApiResponse<Lista[]>> {
    // MOCK TOTAL: Retorna listas fictícias para teste imediato
    return of({
      status: 'sucesso',
      mensagem: 'Listas carregadas (Simulado)',
      dados: [
        { id: 1, usuario_id: 1, nome: 'Compras do Mês' },
        { id: 2, usuario_id: 1, nome: 'Feira de Sábado' },
        { id: 3, usuario_id: 1, nome: 'Farmácia' }
      ]
    });
  }

  buscarListas(usuarioId: number): Observable<ApiResponse<Lista[]>> {
    return this.http.get<ApiResponse<Lista[]>>(`${this.baseUrl}/listas/usuario/${usuarioId}`);
  }

  // --- PRODUTOS ---
  buscarProduto(codigoBarras: string): Observable<ApiResponse<Produto>> {
    // MOCK TOTAL: Retorna um produto genérico para qualquer código
    return of({
      status: 'sucesso',
      mensagem: 'Produto encontrado (Simulado)!',
      dados: {
        id: Math.floor(Math.random() * 1000),
        codigo_barras: codigoBarras,
        nome: 'Produto de Teste ' + codigoBarras,
        marca: 'Marca Genérica',
        categoria: 'Diversos',
        peso: '1kg'
      }
    });
  }

  adicionarItem(listaId: number, produtoId: number, quantidade = 1): Observable<ApiResponse<{ id: number }>> {
    // MOCK TOTAL: Adiciona o item instantaneamente sem servidor
    return of({
      status: 'sucesso',
      mensagem: 'Produto adicionado com sucesso (Simulado)!',
      dados: { id: produtoId }
    });
  }

  // --- IA ---
  obterSugestao(listaId: number): Observable<ApiResponse<{ sugestao: string }>> {
    return this.http.post<ApiResponse<{ sugestao: string }>>(`${this.baseUrl}/ia/sugestao-lista`, {
      lista_id: listaId
    });
  }
}
