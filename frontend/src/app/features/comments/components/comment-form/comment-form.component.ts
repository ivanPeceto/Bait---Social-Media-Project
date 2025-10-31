// Archivo: src/app/features/comments/components/comment-form/comment-form.component.ts
import { Component, EventEmitter, Output, inject, Input, OnInit } from '@angular/core'; 
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';

@Component({
  selector: 'app-comment-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './comment-form.component.html',
})
export class CommentFormComponent implements OnInit { 
  @Input() initialContent: string = ''; 
  @Input() isEditing: boolean = false;  
  @Output() commentSubmitted = new EventEmitter<string>();
  @Output() formCancelled = new EventEmitter<void>(); 

  private fb = inject(FormBuilder);

  commentForm = this.fb.group({
    content_comments: ['', [Validators.required, Validators.maxLength(280)]] 
  });

  submitButtonText: string = 'Comentar'; 

  ngOnInit(): void {
    if (this.initialContent) {
      this.commentForm.patchValue({ content_comments: this.initialContent });
    }
    if (this.isEditing) {
      this.submitButtonText = 'Guardar';
    }
  }

  onSubmit(): void {
    console.log('[Form] onSubmit function CALLED!'); 
    console.log('[Form] Value on Submit:', this.commentForm.value); 
    console.log('[Form] Is Form Valid?', this.commentForm.valid);        

    if (this.commentForm.valid && this.commentForm.value.content_comments) { 
      console.log('[Form] Emitting value:', this.commentForm.value.content_comments); 
      this.commentSubmitted.emit(this.commentForm.value.content_comments); 
      
      if (!this.isEditing) {
        this.commentForm.reset();
      }
    } else {
      console.error('[Form] Submit failed. Valid:', this.commentForm.valid, 'Value:', this.commentForm.value.content_comments); 
    }
  }

  cancel(): void {
    console.log('[Form] Cancel button clicked'); 
    this.formCancelled.emit();
  }
}