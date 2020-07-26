import { Component, OnInit } from '@angular/core';
import { ManageElectionService } from '../manage-election.service';
import { DBTransactions } from '../../core/db_transactions.service';
import { http_response } from 'src/app/core/models/http_response.model';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-manage-election',
  templateUrl: './manage-election.component.html',
  styleUrls: ['./manage-election.component.css']
})
export class ManageElectionComponent implements OnInit {
  tally_response: string;

  constructor(private election_manager: ManageElectionService,
              private trans: DBTransactions,
              private route: ActivatedRoute) { }

  ngOnInit() {
  }

  calc_election(){
    if(this.election_manager.election.election_id){
      this.trans.calc_election(this.election_manager.election.election_id)
        .subscribe(
          (http_response: http_response) => {
            this.tally_response = http_response.message;
          }
        );
    }
  }
}
