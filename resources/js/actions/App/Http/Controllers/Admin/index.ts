import ManagementClusterController from './ManagementClusterController'
import ProviderController from './ProviderController'

const Admin = {
    ManagementClusterController: Object.assign(ManagementClusterController, ManagementClusterController),
    ProviderController: Object.assign(ProviderController, ProviderController),
}

export default Admin