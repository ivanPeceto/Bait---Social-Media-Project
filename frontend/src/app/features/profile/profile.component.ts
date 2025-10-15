import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { Observable } from 'rxjs';
import { User } from '../../core/models/user.model';
import { ProfileService } from './services/profile.service';
import { map, tap } from 'rxjs';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterLink], 
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss']
})
export class ProfileComponent implements OnInit {
  private profileService = inject(ProfileService);

  userProfile$!: Observable<User>;
  
  isOwnProfile: boolean = true;

  ngOnInit(): void {
    this.userProfile$ = this.profileService.getOwnProfile().pipe(
      tap(user => console.log('Datos del perfil recibidos:', user)),
      map((response: any) => response.data)
    );
    
  }
}