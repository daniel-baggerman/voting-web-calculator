import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { Params, ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-reporting',
  templateUrl: './reporting.component.html',
  styleUrls: ['./reporting.component.css']
})
export class ReportingComponent implements OnInit {

  show_reporting = false;

  constructor(private route: ActivatedRoute) { }

  ngOnInit() {
    // subscribe to the route params 
    this.route.params.subscribe(
      (params: Params) => {
        if (params['election_id']){
          this.election_selected(params['election_id']);
        } else {
          this.show_reporting = false;
        }
      }
    );
  }

  election_selected(election_id: number){
    // show election details
    this.show_reporting = true;
  }
}
