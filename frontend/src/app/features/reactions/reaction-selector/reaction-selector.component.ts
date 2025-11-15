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
export class ReactionSelectorComponent {
  /** Controla si el menú es visible o no */
  @Input() visible: boolean = false;
  
  /** Emite la reacción seleccionada cuando el usuario hace clic */
  @Output() reactionSelected = new EventEmitter<ReactionType>();

  @Input() reactionTypes: ReactionType[] = [];

  /**
   * Se llama cuando el usuario hace clic en un ícono
   * @param reaction El objeto ReactionType completo
   */
  onSelect(reaction: ReactionType): void {
    this.reactionSelected.emit(reaction);
  }
}