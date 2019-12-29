import { Component, OnInit, OnDestroy } from '@angular/core';
import { BpBallotService } from './bp-ballot.service';
import { DBTransactions } from '../../db_transactions.service';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-beatpath-ballot-cast',
  templateUrl: './beatpath-ballot-cast.component.html',
  styleUrls: ['./beatpath-ballot-cast.component.css']
})
export class BeatpathBallotCastComponent implements OnInit, OnDestroy {
  elections = [];

  // used to look for when ballot is successfully submitted and then display a message when it is.
  successful_ballot_submittion_sub: Subscription;
  submission_message: string = "";

  constructor(private bp_ballot_service: BpBallotService,
              private trans: DBTransactions) 
  {}

  ngOnInit() {
    // retrieve the elections for the dropdown
    this.trans.get_elections().subscribe((data: any) => {
      this.elections = data;
    });

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

  election_selected(election_id: number){
    // populate the available options on the ballot
    this.bp_ballot_service.set_election_options(election_id);
    this.bp_ballot_service.election_id = election_id;

    // clear any ballot options already selected
    this.bp_ballot_service.clear_ballot();
  }

  submit_ballot(){
    this.bp_ballot_service.submit_ballot();
  }

  clear_ballot(){
    this.bp_ballot_service.clear_ballot();
  }

}
