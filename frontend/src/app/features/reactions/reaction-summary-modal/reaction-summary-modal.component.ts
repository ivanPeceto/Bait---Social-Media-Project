import { Component, Input, Output, EventEmitter, inject } from '@angular/core';
import { CommonModule, TitleCasePipe } from '@angular/common';
import { ReactionIconComponent } from '../reaction-icon/reaction-icon.component';
import { PostService } from '../../../core/services/post.service';
import { ReactionSummary } from '../../../core/models/reaction-type.model';

@Component({
  selector: 'app-reaction-summary-modal',
  standalone: true,
  imports: [
    CommonModule,
    TitleCasePipe, 
    ReactionIconComponent
  ],
  templateUrl: './reaction-summary-modal.component.html',
  styleUrls: ['./reaction-summary-modal.component.scss']
})
export class ReactionSummaryModalComponent {

  /** Controla si el modal es visible */
  @Input() visible: boolean = false;

  /** El array de conteos que viene de la API */
  @Input() summary: ReactionSummary[] | null = null;
  
  /** Muestra un spinner mientras el padre busca los datos */
  @Input() isLoading: boolean = true;

  /** Emite un evento para que el padre cierre el modal */
  @Output() close = new EventEmitter<void>();

  /**
   * Emite el evento 'close'
   */
  onClose(): void {
    this.close.emit();
  }

  /**
   * Detiene la propagación del clic para que al hacer clic
   * en el contenido del modal, no se cierre.
   */
  stopPropagation(event: MouseEvent): void {
    event.stopPropagation();
  }

  /**
   * Da el color correcto al ícono.
   */
  getReactionColor(name: string): string {
    if (!name) return '#6b7280'; 

    switch(name.toLowerCase()) {
      case 'like': return '#007bff';
      case 'love': return '#e0245e';
      case 'haha': return '#f4b400';
      case 'wow': return '#1da1f2';
      case 'sad': return '#ffad1f';
      case 'angry': return '#d93a00';
      default: return '#6b7280';
    }
  }
}