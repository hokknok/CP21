import { Component, Input, OnInit } from '@angular/core';
import { ChartOptions } from '../../interfaces/chart-options';

@Component({
  selector: ' app-graph-line',
  templateUrl: './graph-line.component.html',
  styleUrls: ['./graph-line.component.scss'],
})
export class GraphLineComponent implements OnInit {
  @Input() public chartOptions: Partial<ChartOptions>;

  constructor() {}

  public ngOnInit(): void {
    console.log(this.chartOptions);
  }
}
