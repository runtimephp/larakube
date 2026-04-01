import Api from './Api'
import OrganizationController from './OrganizationController'
import SwitchOrganizationController from './SwitchOrganizationController'
import Settings from './Settings'
import Auth from './Auth'

const Controllers = {
    Api: Object.assign(Api, Api),
    OrganizationController: Object.assign(OrganizationController, OrganizationController),
    SwitchOrganizationController: Object.assign(SwitchOrganizationController, SwitchOrganizationController),
    Settings: Object.assign(Settings, Settings),
    Auth: Object.assign(Auth, Auth),
}

export default Controllers