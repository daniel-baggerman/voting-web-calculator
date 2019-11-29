import { Injectable } from '@angular/core';
import { bpOption } from '../bp_models/bp_option.model';
import { Subject } from 'rxjs';
import { DBTransactions } from '../../db_transactions.service';



@Injectable({
  providedIn: 'root'
})
export class BpBallotService {
  private election_options: bpOption[] = [
    // {description:"Aachis Indian",option_id:1,enabled:1},
    // {description:"Bowl Lab",option_id:2,enabled:1},
    // {description:"Chipotle",option_id:3,enabled:1},
    // {description:"Doc Green's",option_id:4,enabled:1},
    // {description:"Einstein's Bagels",option_id:5,enabled:1}
  ];
  private selected_options: bpOption[] = [];

  election_id: number;

  election_options_changed = new Subject<void>();
  selected_options_changed = new Subject<void>();

  constructor(private trans: DBTransactions) { }

  get_selected_options(){
    return this.selected_options.slice();
  }

  get_election_options(){
    return this.election_options.slice();
  }

  set_election_options(election_id: number){
    this.trans.get_election_options(election_id).subscribe((data: any) => {
      this.election_options = data;
      this.election_options_changed.next();
    });
  }

  add_to_ballot(option: bpOption){
    if (option.enabled) {
      // add the option to the selected_options array and disable it from the ballot options
      this.election_options[option.option_id - 1].enabled = 0;
      this.selected_options.push(option);
      // emit the changed event, so that ballot can react
      this.selected_options_changed.next();
    }
  }

  clear_ballot(){
    // Empty the ia_ballot array to clear all selected choices
    this.selected_options = [];
            
    // Reset all the options to be enabled
    for (var i = 0; i < this.election_options.length; i++) {
      this.election_options[i].enabled = 1;
    }

    // emit the changed event, so that ballot can react
    this.selected_options_changed.next();
  }

  submit_ballot(){
    this.trans.submit_ballot(this.election_id, 1, this.selected_options).subscribe();
  }

  move_option_up(index: number){
    if (index > 0) {
      let itemToMove = this.selected_options[index];
      this.selected_options.splice(index, 1);
      this.selected_options.splice(index - 1, 0, itemToMove);
      // emit the changed event, so that ballot can react
      this.selected_options_changed.next();
    }
  }

  move_option_down(index: number){
    if (index < this.selected_options.length - 1) {
      let itemToMove = this.selected_options[index];  
      this.selected_options.splice(index, 1);
      this.selected_options.splice(index + 1, 0, itemToMove);
      // emit the changed event, so that ballot can react
      this.selected_options_changed.next();
    }
  }

  remove_option(option: bpOption, index: number){
    // Function to check if the ballot item's ID matches the ID of the option when we search through the ia_options
    function matchesId(item: bpOption) {
      return item.option_id === option.option_id;
    };
    
    // Variables to remember each index more easily
    let ballotIndex = index,
        optionsIndex = this.selected_options.findIndex(matchesId);
    
    // Re-enable the option that we're removing from the ballot
    this.selected_options[optionsIndex].enabled = 1;
    
    // Remove the item from the ballot
    this.selected_options.splice(ballotIndex, 1);
    
    // emit the changed event, so that ballot can react
    this.selected_options_changed.next();
  }
}
