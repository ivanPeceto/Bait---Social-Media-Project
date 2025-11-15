export interface ReactionType {
  id: number;
  name: string;
}

export interface ReactionSummary {
  reaction_type_id: number;
  name: string;
  count: number;
}