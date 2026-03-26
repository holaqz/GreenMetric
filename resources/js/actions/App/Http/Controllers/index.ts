import CycleController from './CycleController'
import IndicatorResponseController from './IndicatorResponseController'
import FileController from './FileController'
import ReportController from './ReportController'
import IndicatorExportController from './IndicatorExportController'
import Settings from './Settings'
const Controllers = {
    CycleController: Object.assign(CycleController, CycleController),
IndicatorResponseController: Object.assign(IndicatorResponseController, IndicatorResponseController),
FileController: Object.assign(FileController, FileController),
ReportController: Object.assign(ReportController, ReportController),
IndicatorExportController: Object.assign(IndicatorExportController, IndicatorExportController),
Settings: Object.assign(Settings, Settings),
}

export default Controllers