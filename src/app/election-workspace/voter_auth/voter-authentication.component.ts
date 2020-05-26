import { Component, OnInit } from '@angular/core';
import { ManageElectionService } from '../manage-election.service';
import { NgForm } from '@angular/forms';
import { AuthenticationService } from 'src/app/auth-guard/authentication.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-voter-authentication',
  templateUrl: './voter-authentication.component.html',
  styleUrls: ['./voter-authentication.component.css']
})
export class VoterAuthenticationComponent implements OnInit {
  election_type: string;
  url_election_name: string;
  error_message: string = null;

  constructor(public election_manager: ManageElectionService,
              private auth_service: AuthenticationService,
              private router: Router) { }

  ngOnInit() {
    // Define the type of validation options the user gets
    let password_protect = +this.election_manager.election.password_protect,
        public_private = +this.election_manager.election.public_private;

    if ( public_private == 0 ){
      this.election_type = 'private';
    } else 
    if ( public_private == 1 && password_protect == 1 ){
      this.election_type = 'public';
    } else {
      console.log('public_private');
      console.log(this.election_manager.election.public_private);
      console.log('password_protect');
      console.log(this.election_manager.election.password_protect);
    }
  }

  submit(form: NgForm) {
    this.auth_service.login(this.election_manager.election.url_election_name, form.value.code)
    .subscribe(
      resp => {
        this.router.navigate([this.election_manager.election.url_election_name,'vote'])
      },
      error => {
        console.log(error);

        this.error_message = error.error;
      }
    );
  }

}
