import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { NgForm } from '@angular/forms';
import { DBTransactions } from '../db_transactions.service';
import { election } from '../shared/election.model';

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
    this.trans.get_elections_like(form.value.name)
    .subscribe(
      (data: {election_id: number, description: string, url_election_name: string}[]) => {
        this.searched = true;
        this.elections = data;
      },
      (error) => {
        alert(error.message);
        console.error(error);
      }
    );
  }

  election_selected(election_id: number){
    this.election_selected_from_search.emit({election_id: election_id});
  }
}
