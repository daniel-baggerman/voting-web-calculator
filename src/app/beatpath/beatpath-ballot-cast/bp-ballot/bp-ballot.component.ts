import { Component, OnInit, OnDestroy } from '@angular/core';
import { bpOption } from '../../bp_models/bp_option.model';
import { Subscription } from 'rxjs';
import { BpBallotService } from '../bp-ballot.service';

@Component({
  selector: 'app-bp-ballot',
  templateUrl: './bp-ballot.component.html',
  styleUrls: ['./bp-ballot.component.css']
})
export class BpBallotComponent implements OnInit, OnDestroy {
  selected_options: bpOption[];

  private selected_options_sub: Subscription;

  constructor(private bp_ballot_service: BpBallotService) { }

  ngOnInit() {
    // initialize the selected options. Should be empty. And subscribe to the selected_options array in the service
    this.selected_options = this.bp_ballot_service.get_selected_options();
    this.selected_options_sub = this.bp_ballot_service.selected_options_changed.subscribe(
      () => {
        this.selected_options = this.bp_ballot_service.get_selected_options();
        }
    );
  }

  ngOnDestroy() {
    this.selected_options_sub.unsubscribe();
  }

  move_option_up(index: number){
    this.bp_ballot_service.move_option_up(index);
  }

  move_option_down(index: number){
    this.bp_ballot_service.move_option_down(index);
  }

  remove_option(option: bpOption, index:number){
    this.bp_ballot_service.remove_option(option,index);
  }
}
