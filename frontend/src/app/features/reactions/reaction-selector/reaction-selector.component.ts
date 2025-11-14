import { Component, EventEmitter, Input, Output, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Observable, of } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';
import { ReactionTypeService } from '../../../core/services/reaction-type.service';
import { ReactionType } from '../../../core/models/reaction-type.model';
import { ReactionIconComponent } from '../reaction-icon/reaction-icon.component';

@Component({
  selector: 'app-reaction-selector',
  standalone: true,
  imports: [CommonModule, ReactionIconComponent],
  templateUrl: './reaction-selector.component.html',
  styleUrls: ['./reaction-selector.component.scss']
})
export class ReactionSelectorComponent implements OnInit {
  /** Controla si el menú es visible o no */
  @Input() visible: boolean = false;
  
  /** Emite la reacción seleccionada cuando el usuario hace clic */
  @Output() reactionSelected = new EventEmitter<ReactionType>();

  private reactionTypeService = inject(ReactionTypeService);
  
  public reactionTypes$: Observable<ReactionType[]>;
  public isLoading = true;

  ngOnInit(): void {
    // Carga los tipos de reacción desde el servicio que creamos
    this.reactionTypes$ = this.reactionTypeService.getReactionTypes().pipe(
      tap(() => this.isLoading = false),
      catchError((error: any) => {
        this.isLoading = false;
        console.error('Error al cargar los tipos de reacción:', error);
        return of([]); 
      })
    );
  }

  /**
   * Se llama cuando el usuario hace clic en un ícono
   * @param reaction El objeto ReactionType completo
   */
  onSelect(reaction: ReactionType): void {
    this.reactionSelected.emit(reaction);
  }
}