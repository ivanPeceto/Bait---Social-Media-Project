import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'reactionIdToName',
  standalone: true
})
export class ReactionIdToNamePipe implements PipeTransform {
  private readonly reactionMap: { [key: number]: string } = {
    1: 'like',
    2: 'love',
    3: 'haha',
    4: 'wow',
    5: 'sad',
    6: 'angry',
  };

  /**
   * Transforma un reaction_type_id en un nombre de string.
   * @param id El ID numérico de la reacción.
   * @returns El nombre en string (ej: 'like') o 'like' como default.
   */
  transform(id: number | null | undefined): string {
    if (id === null || id === undefined) {
      return 'like'; 
    }
    return this.reactionMap[id] || 'like';
  }
}