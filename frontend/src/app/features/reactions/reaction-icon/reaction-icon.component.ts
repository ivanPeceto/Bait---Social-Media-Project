import { Component, Input, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-reaction-icon',
  standalone: true,
  imports: [CommonModule],
  template: `
    <svg 
      xmlns="http://www.w3.org/2000/svg" 
      viewBox="0 0 24 24" 
      stroke-width="1.5"
      [attr.fill]="fill" 
      [attr.stroke]="stroke"
      [attr.width]="size" 
      [attr.height]="size"
      [ngSwitch]="reactionName">

      <path *ngSwitchCase="'like'" 
        stroke-linecap="round" stroke-linejoin="round"
        d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a1 1 0 0 1 -1 -1z" />
        
      <path *ngSwitchCase="'love'" 
        stroke-linecap="round" stroke-linejoin="round"
        d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" />

      <path *ngSwitchCase="'haha'" 
        stroke-linecap="round" stroke-linejoin="round"
        d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0m4.5 4.5c1.5 1 3.5 1.5 5.5 1.5s4 -0.5 5.5 -1.5m-9.5 -4.5c-.5 0 -1 -0.5 -1 -1s0.5 -1 1 -1s1 0.5 1 1s-0.5 1 -1 1m6 0c-.5 0 -1 -0.5 -1 -1s0.5 -1 1 -1s1 0.5 1 1s-0.5 1 -1 1" />

      <path *ngSwitchCase="'wow'" 
        stroke-linecap="round" stroke-linejoin="round"
        d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0m-2.5 -2.5c-.5 0 -1 -0.5 -1 -1s0.5 -1 1 -1s1 0.5 1 1s-0.5 1 -1 1m6 0c-.5 0 -1 -0.5 -1 -1s0.5 -1 1 -1s1 0.5 1 1s-0.5 1 -1 1m-3 5.5c-2 0 -4 -1 -5 -2.5" />

      <path *ngSwitchCase="'sad'" 
        stroke-linecap="round" stroke-linejoin="round"
        d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0m3 -2c-.5 0 -1 -0.5 -1 -1s0.5 -1 1 -1s1 0.5 1 1s-0.5 1 -1 1m6 0c-.5 0 -1 -0.5 -1 -1s0.5 -1 1 -1s1 0.5 1 1s-0.5 1 -1 1m-5 5.5c1.5 -1 3.5 -1.5 5.5 -1.5s4 0.5 5.5 1.5" />

      <path *ngSwitchCase="'angry'" 
        stroke-linecap="round" stroke-linejoin="round"
        d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0m-2.5 -2.5c-.5 0 -1 -0.5 -1 -1s0.5 -1 1 -1s1 0.5 1 1s-0.5 1 -1 1m6 0c-.5 0 -1 -0.5 -1 -1s0.5 -1 1 -1s1 0.5 1 1s-0.5 1 -1 1m2.5 5.5c-2 0 -4 -1 -5 -2.5m10 0c-.8 -1.5 -2.8 -2.5 -5 -2.5s-4.2 1 -5 2.5" />

      <path *ngSwitchDefault 
        stroke-linecap="round" stroke-linejoin="round"
        d="M7 11v8a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-7a1 1 0 0 1 1 -1h3a4 4 0 0 0 4 -4v-1a2 2 0 0 1 4 0v5h3a2 2 0 0 1 2 2l-1 5a2 3 0 0 1 -2 2h-7a1 1 0 0 1 -1 -1z" />
    </svg>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ReactionIconComponent {
  /** El nombre de la reacción (ej: 'like', 'love') */
  @Input() reactionName: string = 'like';
  
  /** El tamaño del SVG (ej: 24, "1.5rem") */
  @Input() size: string | number = '24';
  
  /** El color del borde del SVG. 'currentColor' lo hereda del texto. */
  @Input() stroke: string = 'currentColor';
  
  /** El color de relleno. 'none' es lo mejor para íconos de línea. */
  @Input() fill: string = 'none';
}