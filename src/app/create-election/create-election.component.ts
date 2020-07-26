import { Component, OnInit, ViewChild } from '@angular/core';
import { DBTransactions } from '../core/db_transactions.service';
import { election } from '../core/models/election.model';
import { NgForm } from '@angular/forms';
import { http_response } from 'src/app/core/models/http_response.model'

@Component({
  selector: 'app-create-election',
  templateUrl: './create-election.component.html',
  styleUrls: ['./create-election.component.css']
})
export class CreateElectionComponent implements OnInit {
  @ViewChild('f',{static: false}) election_form: NgForm;
  create_election_response_msg: string = "";
  election_created: boolean;
  election: election;
  options: string[];

  form: any = {};

  constructor(private trans: DBTransactions) { }

  ngOnInit() {
  }

  validate_start_date(){
    this.election_form.controls['start_date'].updateValueAndValidity();
  }

  validate_end_date(){
    this.election_form.controls['end_date'].updateValueAndValidity();
  }

  create_election(){
    /* example of expected postdata
    {
        desc: "This is a long description."
        end_date: "2020-04-09"
        name: "Test Ballot 3"
        options: "a;b;c;d"
        start_date: "2020-04-07" // optional
        public_private: "public" || "private"
        // these two if public
        password_protect: false
        password: arst // only if password_protect = true
        // this if private
        email: "adam@test.com,barb@test.com"
    }
    */

    console.log(this.form);

    this.trans.create_election(this.form).subscribe(
      (http_response: http_response) => {
        console.log(http_response);
        this.create_election_response_msg = http_response.message;
        this.election_created = true;
        this.election = http_response.data.election;
        this.options = http_response.data.options;
      }
    );
  }
}
