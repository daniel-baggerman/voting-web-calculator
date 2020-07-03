import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http'

import { AppComponent } from './app.component';
import { HeaderComponent } from './header/header.component';
import { BeatpathBallotCastComponent } from './election-workspace/beatpath/beatpath-ballot-cast/beatpath-ballot-cast.component';
import { AppRoutingModule } from './app-routing.module';
import { BpElectionOptionsComponent } from './election-workspace/beatpath/beatpath-ballot-cast/bp-election-options/bp-election-options.component';
import { BpBallotComponent } from './election-workspace/beatpath/beatpath-ballot-cast/bp-ballot/bp-ballot.component';
import { HomePageComponent } from './home-page/home-page.component';
import { CreateElectionComponent } from './create-election/create-election.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { SearchElectionsComponent } from './search-elections/search-elections.component';
import { ManageElectionComponent } from './election-workspace/manage-election/manage-election.component';
import { ReportingComponent } from './election-workspace/reporting/reporting.component';
import { BeatpathGraphComponent } from './election-workspace/reporting/beatpath-graph/beatpath-graph.component';
import { FooterComponent } from './footer/footer.component';
import { ElectionWorkspaceComponent } from './election-workspace/election-workspace.component';
import { EmailListValidatorDirective } from './helpers/email-list-validator.directive';
import { EndPastStartDirective } from './helpers/end-past-start.directive';
import { StartBeforeEndDirective } from './helpers/start-before-end.directive';
import { VoterAuthenticationComponent } from './election-workspace/voter_auth/voter-authentication.component';
import { PrivateBallotVoterAuthComponent } from './election-workspace/private-ballot-voter-auth/private-ballot-voter-auth.component';
import { AuthGuard } from './auth-guard/auth.guard';
import { AuthInterceptorService } from './auth-guard/auth-interceptor.service';
import { JwtHelperService } from '@auth0/angular-jwt';
import { HowItWorksComponent } from './how-it-works/how-it-works.component';
import { TallyMethodComponent } from './how-it-works/tally-method/tally-method.component';
import { RankedChoiceBallotComponent } from './how-it-works/ranked-choice-ballot/ranked-choice-ballot.component';
import { AboutSecurityComponent } from './how-it-works/about-security/about-security.component';
import { ErrorIntercept } from './error.interceptor';

@NgModule({
  declarations: [
    AppComponent,
    HeaderComponent,
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
    FooterComponent,
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
    FormsModule,
    AppRoutingModule,
    HttpClientModule
  ],
  providers: [AuthGuard,
              {provide: HTTP_INTERCEPTORS, useClass: AuthInterceptorService, multi: true},
              {provide: HTTP_INTERCEPTORS, useClass: ErrorIntercept, multi: true},
              JwtHelperService],
  bootstrap: [AppComponent]
})
export class AppModule { }
