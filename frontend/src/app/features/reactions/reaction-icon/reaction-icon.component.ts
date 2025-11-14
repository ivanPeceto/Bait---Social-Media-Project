import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-reaction-icon',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './reaction-icon.component.html',
  styleUrls: ['./reaction-icon.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ReactionIconComponent {
  /** El nombre de la reacción (ej: 'like', 'love') */
  @Input() reactionName: string = 'like';
}