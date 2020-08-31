import { Component, OnInit, OnDestroy } from '@angular/core';
import { BpBallotService } from './bp-ballot.service';
import { Subscription } from 'rxjs';
import { ManageElectionService } from 'src/app/election-workspace/manage-election.service';
import { MatDialog, MatDialogConfig } from '@angular/material/dialog';
import { DialogModalComponent } from 'src/app/shared/dialog-modal/dialog-modal.component';


@Component({
  selector: 'app-beatpath-ballot-cast',
  templateUrl: './beatpath-ballot-cast.component.html',
  styleUrls: ['./beatpath-ballot-cast.component.css']
})
export class BeatpathBallotCastComponent implements OnInit, OnDestroy {
  // used to look for when ballot is successfully submitted and then display a message when it is.
  successful_ballot_submittion_sub: Subscription;
  submission_message: string = "";
  submitted_ballot: Array<{}>;

  // Turn off election ballot when election end_date has passed.
  election_date_passed: boolean = true;

  constructor(private bp_ballot_service: BpBallotService,
              public election_manager: ManageElectionService,
              private mat_dialog: MatDialog) {}

  ngOnInit() {
    // Take end date from election, in format 'YYYY-MM-DD', split it and pass it to date object
    const end_date = this.election_manager.election.end_date.split('-');
    const end_date_utc = new Date(+end_date[0],+end_date[1]-1,+end_date[2]+1); // subtract 1 from month because idk my bff jill, and add 1 to day for comparison later since end_date is the last day that votes may be submitted
    const now = new Date();

    // election date passed if end date is less than current date. If passed, then don't show options.
    this.election_date_passed = end_date_utc < now;

    if(this.election_manager.election.election_id && !this.election_date_passed){
      this.bp_ballot_service.set_election_options(this.election_manager.election.election_id);
      this.bp_ballot_service.election_id = this.election_manager.election.election_id;
    }

    // respond to successful ballot submission and display message for user through bound variable
    this.successful_ballot_submittion_sub = this.bp_ballot_service.ballot_successfully_submitted.subscribe(
      (data: { message: string, ballot: [{}] }) => {
        this.submission_message = data.message;
        this.submitted_ballot = data.ballot;
      }
    );
  }

  ngOnDestroy(){
    this.successful_ballot_submittion_sub.unsubscribe();

    // Reset the ballot.
    this.bp_ballot_service.clear_ballot();
  }

  submit_ballot(){

    // only ask for cookies if the user hasn't accepted already

    // https://medium.com/swlh/how-to-create-a-modal-dialog-component-in-angular-8-88028d909be0
    // https://www.techiediaries.com/angular-material-login-form-modal-dialog/
    // https://v5.material.angular.io/components/dialog/overview
    const dialogConfig = new MatDialogConfig();
    dialogConfig.disableClose = true;
    dialogConfig.height = "350px";
    dialogConfig.width = "500px";
    dialogConfig.data = { message: 'This site requires cookies to protect against poll fraud. Do you accept cookies?' }; 

    const dialog_ref = this.mat_dialog.open(DialogModalComponent, dialogConfig);

    dialog_ref.afterClosed().subscribe(result => {
      if(result === 1){
        // give them a cookie :3 then submit ballot

        this.bp_ballot_service.submit_ballot();
      }
    });
  }

  clear_ballot(){
    this.bp_ballot_service.clear_ballot();
  }

}
