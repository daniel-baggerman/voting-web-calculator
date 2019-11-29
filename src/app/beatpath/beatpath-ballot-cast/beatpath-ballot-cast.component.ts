import { Component, OnInit } from '@angular/core';
import { BpBallotService } from './bp-ballot.service';
import { DBTransactions } from '../../db_transactions.service';

@Component({
  selector: 'app-beatpath-ballot-cast',
  templateUrl: './beatpath-ballot-cast.component.html',
  styleUrls: ['./beatpath-ballot-cast.component.css']
})
export class BeatpathBallotCastComponent implements OnInit {
  elections = [];

  constructor(private bp_ballot_service: BpBallotService,
              private trans: DBTransactions) 
  {}

  ngOnInit() {
    this.trans.get_elections().subscribe((data: any) => {
      this.elections = data;
    });
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
