import { Component, OnInit, ViewChild } from '@angular/core';
import { NgForm } from '@angular/forms';
import { DBTransactions } from '../db_transactions.service';
import { election } from '../shared/election.model';
import { NULL_EXPR } from '@angular/compiler/src/output/output_ast';

@Component({
  selector: 'app-create-election',
  templateUrl: './create-election.component.html',
  styleUrls: ['./create-election.component.css']
})
export class CreateElectionComponent implements OnInit {
  @ViewChild('f',{static: false}) election_form: NgForm;

  create_election_response_msg: string = "";

  public_private: string;

  constructor(private trans: DBTransactions) { }

  ngOnInit() {
  }

  create_election(form: NgForm){
    // example form value
    // value: {
    // ​​​  anon: true
    // ​​ ​ desc: "A poll."
    // ​ ​​ halt_date: "2019-12-02"
    // ​ ​​ name: "Test"
    //  ​​ ​options: "A;b;c;d;E"
    //  ​​ ​password: "password"
    //  ​​ ​radioPublicPrivate: "public"
    //  ​​ ​start_date: "2019-12-02"
    // }

    this.trans.create_election(form.value).subscribe(
      (post_response) => {
        this.create_election_response_msg = post_response.post_message;
      },
      (error) => {
        alert( error.error.text );
        // console.error(error);
      }
    );
  }

  clear_form(){
    this.election_form.reset();
  }
}
