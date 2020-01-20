import { Component, OnInit, OnDestroy } from '@angular/core';
import { BpBallotService } from './bp-ballot.service';
import { Subscription } from 'rxjs';
import { ManageElectionService } from 'src/app/manage-election/manage-election.service';

@Component({
  selector: 'app-beatpath-ballot-cast',
  templateUrl: './beatpath-ballot-cast.component.html',
  styleUrls: ['./beatpath-ballot-cast.component.css']
})
export class BeatpathBallotCastComponent implements OnInit, OnDestroy {
  // used to look for when ballot is successfully submitted and then display a message when it is.
  successful_ballot_submittion_sub: Subscription;
  submission_message: string = "";

  constructor(private bp_ballot_service: BpBallotService,
              private election_manager: ManageElectionService) {}

  ngOnInit() {
    if(this.election_manager.election.election_id){
      this.bp_ballot_service.set_election_options(this.election_manager.election.election_id);
      this.bp_ballot_service.election_id = this.election_manager.election.election_id;
    }

    // respond to successful ballot submission and display message for user through bound variable
    this.successful_ballot_submittion_sub = this.bp_ballot_service.ballot_successfully_submitted.subscribe(
      (data: string) => {
        this.submission_message = data;
      }
    );
  }

  ngOnDestroy(){
    this.successful_ballot_submittion_sub.unsubscribe();
  }

  submit_ballot(){
    this.bp_ballot_service.submit_ballot();
  }

  clear_ballot(){
    this.bp_ballot_service.clear_ballot();
  }

}
