import { Component, OnInit, OnDestroy } from '@angular/core';
import { BpBallotService } from './bp-ballot.service';
import { Subscription } from 'rxjs';
import { ActivatedRoute, Params } from '@angular/router';

@Component({
  selector: 'app-beatpath-ballot-cast',
  templateUrl: './beatpath-ballot-cast.component.html',
  styleUrls: ['./beatpath-ballot-cast.component.css']
})
export class BeatpathBallotCastComponent implements OnInit, OnDestroy {
  // used to look for when ballot is successfully submitted and then display a message when it is.
  successful_ballot_submittion_sub: Subscription;
  submission_message: string = "";
  show_ballot: boolean = false;

  constructor(private bp_ballot_service: BpBallotService,
              private route: ActivatedRoute) {}

  ngOnInit() {
    // this.election_selected(this.route.snapshot.params['election_id']);

    this.route.params.subscribe(
      (params: Params) => {
        if (params['election_id']){
          this.election_selected(params['election_id']);
        } else {
          console.log('test');
          this.show_ballot = false;
        }
      }
    );

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

    // show ballot options and ballot
    this.show_ballot = true;
  }

  submit_ballot(){
    this.bp_ballot_service.submit_ballot();
  }

  clear_ballot(){
    this.bp_ballot_service.clear_ballot();
  }

}
