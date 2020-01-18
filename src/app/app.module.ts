import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http'

import { AppComponent } from './app.component';
import { HeaderComponent } from './header/header.component';
import { BeatpathBallotCastComponent } from './beatpath/beatpath-ballot-cast/beatpath-ballot-cast.component';
import { AppRoutingModule } from './app-routing.module';
import { BpElectionOptionsComponent } from './beatpath/beatpath-ballot-cast/bp-election-options/bp-election-options.component';
import { BpBallotComponent } from './beatpath/beatpath-ballot-cast/bp-ballot/bp-ballot.component';
import { HomePageComponent } from './home-page/home-page.component';
import { CreateElectionComponent } from './create-election/create-election.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { SearchElectionsComponent } from './search-elections/search-elections.component';
import { AuthenticationService } from './authentication.service';
import { ManageElectionComponent } from './manage-election/manage-election.component';
import { ReportingComponent } from './reporting/reporting.component';
import { BeatpathGraphComponent } from './reporting/beatpath-graph/beatpath-graph.component';

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
    BeatpathGraphComponent
  ],
  imports: [
    BrowserModule,
    FormsModule,
    AppRoutingModule,
    HttpClientModule
  ],
  providers: [AuthenticationService],
  bootstrap: [AppComponent]
})
export class AppModule { }
