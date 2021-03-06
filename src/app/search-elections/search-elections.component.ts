import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { NgForm } from '@angular/forms';
import { DBTransactions } from '../core/db_transactions.service';
import { election } from '../core/models/election.model';
import { http_response } from 'src/app/core/models/http_response.model';

@Component({
  selector: 'app-search-elections',
  templateUrl: './search-elections.component.html',
  styleUrls: ['./search-elections.component.css']
})
export class SearchElectionsComponent implements OnInit {
  searched: boolean = false;
  elections: election[];
  @Output('election_selected') election_selected_from_search = new EventEmitter<{election_id: number}>();

  constructor(private trans: DBTransactions) { }

  ngOnInit() { }

  get_elections_like(form: NgForm){
    this.trans.get_elections_like(form.value.searched_election)
    .subscribe(
      (http_response: http_response) => {
        this.searched = true;
        this.elections = http_response.data.elections;
      }
    );
  }
}
