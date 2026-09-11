import { HttpClient, HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, throwError } from 'rxjs';
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
    // usuario_id agora é injetado pelo token via AuthInterceptor
    return this.http.post<ApiResponse<Lista>>(`${this.baseUrl}/listas`, { nome });
  }

  listarMinhasListas(): Observable<ApiResponse<Lista[]>> {
    return this.http.get<ApiResponse<Lista[]>>(`${this.baseUrl}/listas`);
  }

  buscarListas(usuarioId: number): Observable<ApiResponse<Lista[]>> {
    return this.http.get<ApiResponse<Lista[]>>(`${this.baseUrl}/listas/usuario/${usuarioId}`);
  }

  // --- PRODUTOS ---
  buscarProduto(codigoBarras: string): Observable<ApiResponse<Produto>> {
    return this.http.get<ApiResponse<Produto>>(`${this.baseUrl}/produtos/${encodeURIComponent(codigoBarras)}`);
  }

  adicionarItem(listaId: number, produtoId: number, quantidade = 1): Observable<ApiResponse<{ id: number }>> {
    return this.http.post<ApiResponse<{ id: number }>>(`${this.baseUrl}/listas/${listaId}/itens`, {
      produto_id: produtoId,
      quantidade
    });
  }

  // --- IA ---
  obterSugestao(listaId: number): Observable<ApiResponse<{ sugestao: string }>> {
    return this.http.post<ApiResponse<{ sugestao: string }>>(`${this.baseUrl}/ia/sugestao-lista`, {
      lista_id: listaId
    });
  }
}
