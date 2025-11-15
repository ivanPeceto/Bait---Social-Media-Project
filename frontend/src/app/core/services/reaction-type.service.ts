import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject, of } from 'rxjs';
import { map, catchError, tap, shareReplay } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { ReactionType } from '../models/reaction-type.model';

@Injectable({
  providedIn: 'root'
})
export class ReactionTypeService {
  private http = inject(HttpClient);
  private apiUrl = environment.apiUrl;

  private reactionTypes$: Observable<ReactionType[]> | null = null;

  getReactionTypes(): Observable<ReactionType[]> {
    if (this.reactionTypes$) {
      return this.reactionTypes$;
    }

    this.reactionTypes$ = this.http.get<any>(`${this.apiUrl}/post-reactions/reaction-types`).pipe(
      map(response => (response.data || []) as ReactionType[]),
      shareReplay(1), // Cachea el resultado
      catchError(() => {
        this.reactionTypes$ = null; 
        return of([]);
      })
    );
    return this.reactionTypes$;
  }
}