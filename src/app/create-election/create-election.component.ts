import { Component, OnInit } from '@angular/core';
import { DBTransactions } from '../db_transactions.service';
import { http_response } from '../shared/http_response.model';

@Component({
  selector: 'app-create-election',
  templateUrl: './create-election.component.html',
  styleUrls: ['./create-election.component.css']
})
export class CreateElectionComponent implements OnInit {
  // @ViewChild('f',{static: false}) election_form: NgForm;
  create_election_response_msg: string = "";
  election_created: boolean;

  form: any = {};

  constructor(private trans: DBTransactions) { }

  ngOnInit() {
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
        this.create_election_response_msg = http_response.message;
        this.election_created = true;
      }
    );
  }
}
