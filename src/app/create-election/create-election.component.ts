import { Component, OnInit, ViewChild } from '@angular/core';
import { NgForm } from '@angular/forms';
import { DBTransactions } from '../db_transactions.service';
import { election } from '../shared/election.model';

@Component({
  selector: 'app-create-election',
  templateUrl: './create-election.component.html',
  styleUrls: ['./create-election.component.css']
})
export class CreateElectionComponent implements OnInit {
  @ViewChild('f',{static: false}) election_form: NgForm;

  constructor(private trans: DBTransactions) { }

  ngOnInit() {
  }

  create_election(form: NgForm){
    let value = form.value;
    // TODO: turn options into array
    let new_election = new election(
        0
      , value.desc
      , value.start_time
      , value.halt_time
      , value.options
      , value.public
      , value.password
      , value.anon);

    this.trans.create_election(new_election);
  }

  clear_form(){
    this.election_form.reset();
  }
}
