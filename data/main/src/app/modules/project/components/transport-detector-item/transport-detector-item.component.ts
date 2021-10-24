import { Component, OnInit } from '@angular/core';
import { UntilDestroy, untilDestroyed } from '@ngneat/until-destroy';
import { combineLatest } from 'rxjs';
import { map, switchMap, tap } from 'rxjs/operators';

import { ChartOptions } from '../../interfaces/chart-options';
import { GraphDataMap } from '../../interfaces/graph-data-item';
import { TransportDetector } from '../../interfaces/transport-detector';
import { WebCamItem } from '../../interfaces/web-cam-item';
import { TransportDetectorDataService } from '../../services/transport-detector-data/transport-detector-data.service';
import { TransportDetectorService } from '../../services/transport-detector/transport-detector.service';
import { WebCamsService } from '../../services/web-cams/web-cams.service';

@UntilDestroy()
@Component({
  selector: 'app-transport-detector-item',
  templateUrl: './transport-detector-item.component.html',
  styleUrls: ['./transport-detector-item.component.scss'],
})
export class TransportDetectorItemComponent implements OnInit {
  public transportDetectorItem: TransportDetector;
  public graphData: GraphDataMap;
  public chartOptions: Partial<ChartOptions>;
  public webCamItem: WebCamItem;
  public webCamList: WebCamItem[] = [];

  constructor(
    private transportDetectorService: TransportDetectorService,
    private transportDetectorDataService: TransportDetectorDataService,
    private webCamsService: WebCamsService,
  ) {}

  public ngOnInit(): void {
    combineLatest([this.transportDetectorService.getList(), this.transportDetectorService.getSelectedItem()])
      .pipe(
        map(([transportDetectorList, selectedItem]) => {
          this.transportDetectorItem = transportDetectorList[selectedItem];

          return [this.transportDetectorItem.id, this.transportDetectorItem.td, this.transportDetectorItem.street];
        }),
        switchMap(([id, dtId, streetId]) =>
          combineLatest([
            this.transportDetectorDataService.getDataMap(dtId, streetId),
            this.webCamsService.getList(id),
          ]).pipe(
            tap(([graphData, webCamList]) => {
              this.graphData = graphData;
              this.webCamList = webCamList;

              this.chartOptions = this.getChartOptions(graphData);
            }),
          ),
        ),
        untilDestroyed(this),
      )
      .subscribe();
  }

  public getChartOptions(graphData): ChartOptions {
    return {
      series: [
        {
          name: 'Загруженность',
          data: graphData.value,
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
        categories: graphData.date,
      },
    };
  }

  public onOpenWebCam(idx: number) {
    this.webCamItem = this.webCamList[idx];
  }

  public onCloseWebCam() {
    this.webCamItem = null;
  }

  public getImageFullPath(file: string): string {
    return `http://traffic-control.digitalninja.ru/upload/data/web-cam/${file}`;
  }
}
