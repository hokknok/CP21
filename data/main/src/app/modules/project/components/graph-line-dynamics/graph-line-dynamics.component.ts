import { Component, OnInit } from '@angular/core';
import { UntilDestroy, untilDestroyed } from '@ngneat/until-destroy';
import { combineLatest, interval } from 'rxjs';
import { map, switchMap, tap } from 'rxjs/operators';
import { ChartOptions } from '../../interfaces/chart-options';
import { GraphDataMap, GraphDataTrendItem } from '../../interfaces/graph-data-item';
import { TransportDetector } from '../../interfaces/transport-detector';
import { TransportDetectorDataService } from '../../services/transport-detector-data/transport-detector-data.service';
import { TransportDetectorService } from '../../services/transport-detector/transport-detector.service';

@UntilDestroy()
@Component({
  selector: 'app-graph-line-dynamics',
  templateUrl: './graph-line-dynamics.component.html',
  styleUrls: ['./graph-line-dynamics.component.scss'],
})
export class GraphLineDynamicsComponent implements OnInit {
  public series: number[] = [];
  public date: string[] = [];

  public trend: boolean;
  public graphData: GraphDataMap;
  public chartOptions: Partial<ChartOptions>;
  public transportDetectorItem: TransportDetector;

  constructor(
    private transportDetectorService: TransportDetectorService,
    private transportDetectorDataService: TransportDetectorDataService,
  ) {}

  public ngOnInit(): void {
    combineLatest([this.transportDetectorService.getList(), this.transportDetectorService.getSelectedItem()])
      .pipe(
        map(([transportDetectorList, selectedItem]) => {
          this.transportDetectorItem = transportDetectorList[selectedItem];

          return [this.transportDetectorItem.id, this.transportDetectorItem.td, this.transportDetectorItem.street];
        }),
        switchMap(([dtId, streetId]) =>
          combineLatest([
            this.transportDetectorDataService.getData(dtId, streetId, 'traffic-congestion'),
            this.transportDetectorDataService.getData<GraphDataTrendItem[]>(dtId, streetId, 'trend'),
          ]).pipe(
            tap(([graphData, trendData]) => {
              graphData.forEach((item, index) => {
                setTimeout(() => {
                  this.transportDetectorDataService.setData(item);
                  this.trend = trendData[index].value;
                }, 3000 * index);
              });
            }),
          ),
        ),
        untilDestroyed(this),
      )
      .subscribe();

    this.transportDetectorDataService
      .getList()
      .pipe(
        tap((item) => {
          this.date.push(item.date);
          this.series.push(item.value);

          this.chartOptions = {
            series: [
              {
                name: 'Загруженность',
                data: this.series,
              },
            ],
            title: {
              text: 'График загруженности',
            },
            chart: {
              width: '100%',
              height: 350,
              type: 'area',
              zoom: {
                enabled: false,
              },
            },
            dataLabels: {
              enabled: false,
            },
            xaxis: {
              categories: this.date,
            },
          };
        }),
        untilDestroyed(this),
      )
      .subscribe();
  }

  private getChartOptions() {}
}
