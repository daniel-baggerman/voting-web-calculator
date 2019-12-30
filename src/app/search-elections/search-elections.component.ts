import { Component, OnInit } from '@angular/core';
import { NgForm } from '@angular/forms';
import { DBTransactions } from '../db_transactions.service';
import { election } from '../shared/election.model';
import { ActivatedRoute, Params } from '@angular/router';

@Component({
  selector: 'app-manage-election',
  templateUrl: './manage-election.component.html',
  styleUrls: ['./manage-election.component.css']
})
export class SearchElectionsComponent implements OnInit {
  searched: boolean = false;
  elections: election[];
  election_id: number;

  constructor(private trans: DBTransactions,
              private route: ActivatedRoute) { }

  ngOnInit() {
    this.election_id = this.route.snapshot.queryParams['election_id'];

    this.route.queryParams.subscribe(
      (queryParams) => {
        this.election_id = queryParams['election_id'];
      }
    );
  }

  search_elections(form: NgForm){
    this.searched = true;

    this.trans.get_elections_like(form.value.name)
    .subscribe(
      (data: any) => {
        this.elections = data;
      },
      (error) => {
        alert(error.message);
        console.error(error);
      }
    );
  }
}
