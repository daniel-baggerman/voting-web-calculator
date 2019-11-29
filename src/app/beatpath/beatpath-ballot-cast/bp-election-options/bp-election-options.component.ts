import { Component, OnInit, OnDestroy } from '@angular/core';
import { bpOption } from '../../bp_models/bp_option.model';
import { Subscription } from 'rxjs';
import { BpBallotService } from '../bp-ballot.service';

@Component({
  selector: 'app-bp-election-options',
  templateUrl: './bp-election-options.component.html',
  styleUrls: ['./bp-election-options.component.css']
})
export class BpElectionOptionsComponent implements OnInit, OnDestroy {
  election_options: bpOption[];

  private election_opitons_sub: Subscription;

  constructor(private bp_ballot_service: BpBallotService) { }

  ngOnInit() {
    // initialize the available election options on the ballot and subscribe to changes.
    this.election_options = this.bp_ballot_service.get_election_options();
    this.election_opitons_sub = this.bp_ballot_service.election_options_changed.subscribe(
      () => {this.election_options = this.bp_ballot_service.get_election_options();}
    );
  }

  ngOnDestroy(){
    this.election_opitons_sub.unsubscribe();
  }

  add_to_ballot(option: bpOption){
    this.bp_ballot_service.add_to_ballot(option);
  }
}
