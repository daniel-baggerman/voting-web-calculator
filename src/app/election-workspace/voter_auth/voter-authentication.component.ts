import { Component, OnInit } from '@angular/core';
import { ManageElectionService } from '../manage-election.service';
import { NgForm } from '@angular/forms';

@Component({
  selector: 'app-voter-authentication',
  templateUrl: './voter-authentication.component.html',
  styleUrls: ['./voter-authentication.component.css']
})
export class VoterAuthenticationComponent implements OnInit {
  election_type: string;

  constructor(private election_manager: ManageElectionService) { }

  ngOnInit() {
    let password_protect = this.election_manager.election.password_protect,
        public_private = this.election_manager.election.public_private;

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
    // authenticate user based on submission.
  }

}
