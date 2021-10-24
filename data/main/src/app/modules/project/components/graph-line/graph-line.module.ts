import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NgApexchartsModule } from 'ng-apexcharts';
import { GraphLineComponent } from './graph-line.component';

@NgModule({
  declarations: [GraphLineComponent],
  imports: [CommonModule, NgApexchartsModule],
  exports: [GraphLineComponent],
})
export class GraphLineModule {}
