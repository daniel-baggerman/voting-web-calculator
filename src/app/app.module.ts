import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { SharedModule } from 'src/app/shared/shared.module';
import { CoreModule } from 'src/app/core/core.module';

import { AppComponent } from './app.component';
import { BeatpathBallotCastComponent } from './election-workspace/beatpath/beatpath-ballot-cast/beatpath-ballot-cast.component';
import { BpElectionOptionsComponent } from './election-workspace/beatpath/beatpath-ballot-cast/bp-election-options/bp-election-options.component';
import { BpBallotComponent } from './election-workspace/beatpath/beatpath-ballot-cast/bp-ballot/bp-ballot.component';
import { HomePageComponent } from './home-page/home-page.component';
import { CreateElectionComponent } from './create-election/create-election.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { SearchElectionsComponent } from './search-elections/search-elections.component';
import { ManageElectionComponent } from './election-workspace/manage-election/manage-election.component';
import { ReportingComponent } from './election-workspace/reporting/reporting.component';
import { BeatpathGraphComponent } from './election-workspace/reporting/beatpath-graph/beatpath-graph.component';
import { ElectionWorkspaceComponent } from './election-workspace/election-workspace.component';

import { EmailListValidatorDirective } from './helpers/email-list-validator.directive';
import { EndPastStartDirective } from './helpers/end-past-start.directive';
import { StartBeforeEndDirective } from './helpers/start-before-end.directive';

import { VoterAuthenticationComponent } from './election-workspace/voter_auth/voter-authentication.component';
import { PrivateBallotVoterAuthComponent } from './election-workspace/private-ballot-voter-auth/private-ballot-voter-auth.component';
import { HowItWorksComponent } from './how-it-works/how-it-works.component';
import { TallyMethodComponent } from './how-it-works/tally-method/tally-method.component';
import { RankedChoiceBallotComponent } from './how-it-works/ranked-choice-ballot/ranked-choice-ballot.component';
import { AboutSecurityComponent } from './how-it-works/about-security/about-security.component';

@NgModule({
  declarations: [
    AppComponent,
    BeatpathBallotCastComponent,
    BpElectionOptionsComponent,
    BpBallotComponent,
    HomePageComponent,
    CreateElectionComponent,
    PageNotFoundComponent,
    SearchElectionsComponent,
    ManageElectionComponent,
    ReportingComponent,
    BeatpathGraphComponent,
    ElectionWorkspaceComponent,
    EmailListValidatorDirective,
    EndPastStartDirective,
    StartBeforeEndDirective,
    VoterAuthenticationComponent,
    PrivateBallotVoterAuthComponent,
    HowItWorksComponent,
    TallyMethodComponent,
    RankedChoiceBallotComponent,
    AboutSecurityComponent
  ],
  imports: [
    BrowserModule,
    SharedModule,
    CoreModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }