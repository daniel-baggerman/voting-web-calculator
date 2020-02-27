import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http'

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
import { AuthenticationService } from './authentication.service';
import { ManageElectionComponent } from './election-workspace/manage-election/manage-election.component';
import { ReportingComponent } from './election-workspace/reporting/reporting.component';
import { BeatpathGraphComponent } from './election-workspace/reporting/beatpath-graph/beatpath-graph.component';
import { FooterComponent } from './footer/footer.component';
import { ElectionWorkspaceComponent } from './election-workspace/election-workspace.component';

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
    ElectionWorkspaceComponent
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
