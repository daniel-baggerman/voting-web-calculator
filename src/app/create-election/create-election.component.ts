import { Component, OnInit, ViewChild } from '@angular/core';
import { NgForm } from '@angular/forms';

@Component({
  selector: 'app-create-election',
  templateUrl: './create-election.component.html',
  styleUrls: ['./create-election.component.css']
})
export class CreateElectionComponent implements OnInit {
  @ViewChild('f',{static: false}) election_form: NgForm;

  constructor() { }

  ngOnInit() {
  }

  create_poll(form: NgForm){
    // do stuff
  }

  clear_form(){
    this.election_form.reset();
  }
}
