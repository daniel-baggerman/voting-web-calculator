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

    console.log(form);

    // let value = form.value;

    // let new_election = new election(
    //     0
    //   , value.desc
    //   , value.start_time
    //   , value.halt_time
    //   , value.options.split(';').filter(function(el: string) {return el != "" && el != null;}) // parse options into array split by ";" then filter out any empty or null elements
    //   , value.public == "public" ? 1 : 0
    //   , value.password
    //   , value.anon ? 1 : 0);

    // this.trans.create_election(new_election);

    this.trans.create_election(form.value).subscribe(
      () => {},
      (error) => {
        alert(error.message);
        console.error(error);
      }
    );
  }

  clear_form(){
    this.election_form.reset();
  }
}
