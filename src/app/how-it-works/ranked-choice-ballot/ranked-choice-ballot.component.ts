import { Component, OnInit } from '@angular/core';
import { ManageElectionService } from 'src/app/election-workspace/manage-election.service';
import { DBTransactions } from 'src/app/db_transactions.service';
import { http_response } from 'src/app/shared/http_response.model';
import { tap } from 'rxjs/operators';

@Component({
  selector: 'app-ranked-choice-ballot',
  templateUrl: './ranked-choice-ballot.component.html',
  styleUrls: ['./ranked-choice-ballot.component.css']
})
export class RankedChoiceBallotComponent implements OnInit {
  election_data_loaded: Promise<boolean>;

  constructor(private election_manager: ManageElectionService,
              private trans: DBTransactions) { }

  ngOnInit() {
    this.trans.get_election_from_url_name('test_ballot_2')
    .pipe(
      tap(
        (http_response: http_response) => {
          if(http_response.data && http_response.data.length !==0){
            this.election_manager.election = {  election_id: http_response.data[0].election_id, 
                                                description: http_response.data[0].description, 
                                                long_description: http_response.data[0].long_description, 
                                                url_election_name: 'test_ballot_2',
                                                public_private: http_response.data[0].public_private,
                                                password_protect: http_response.data[0].password_protect,
                                                start_date: http_response.data[0].start_date,
                                                end_date: http_response.data[0].end_date };
            
            this.election_data_loaded = Promise.resolve(true);
          }
        }
      )
    )
    .subscribe()
  }
}
