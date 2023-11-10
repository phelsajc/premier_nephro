<template>
  <aside id="sidebar" class="main-sidebar sidebar-dark-primary elevation-4">
    <router-link to="/all_employee" class="brand-link">
      <img :src="img" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: 0.8" />
      <span class="brand-text font-weight-light">PREMIER</span> <br>
      <h6 class="font-weight-light">PREMIER NEPHRO CARE SYSTEM INC.</h6>
    </router-link>
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="info">
          <user-info></user-info>
        </div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"
            :class="{ 'menu-open': this.$route.name == 'company_add' || this.$route.name == 'patients_add'  || this.$route.name == 'company_add' || this.$route.name == 'batch_list' || this.$route.name == 'batch_add' || this.$route.name == 'patients_list' || this.$route.name == 'doctors_list' || this.$route.name == 'rproduct_list' || this.$route.name == 'company_list'}">
            <a href="#" class="nav-link"
              :class="{ 'active': this.$route.name == 'patients_list' || this.$route.name == 'patients_add'  || this.$route.name == 'company_add' || this.$route.name == 'batch_list' || this.$route.name == 'batch_add' || this.$route.name == 'doctors_list' || this.$route.name == 'company_list' }">
              <i class="nav-icon fas fa-folder-plus"></i>
              <p>
                Master Files
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <router-link to="/patients_list"> <a href="#" class="nav-link"
                    :class="{ 'active': this.$route.name == 'patients_list' || this.$route.name == 'patients_add' }">
                    <i class="fas fa-user-injured"></i>
                    <p>Patients</p>
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/doctors_list"> <a href="#" class="nav-link"
                    :class="{ 'active': this.$route.name == 'doctors_list' || this.$route.name == 'doctors_add' }">
                    <i class="fas fa-user-md"></i>
                    Doctors
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/batch_list"> <a href="#" class="nav-link"
                    :class="{ 'active': this.$route.name == 'batch_list' || this.$route.name == 'batch_add' }">
                    <i class="fas fa-equals"></i>
                    Batches
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/company_list"> <a href="#" class="nav-link"
                    :class="{ 'active': this.$route.name == 'company_list' || this.$route.name == 'company_add' }">
                    <i class="fas fa-building"></i>
                    Company
                  </a></router-link>
              </li>
              <!-- <li class="nav-item">
                <router-link to="/product_list"> <a href="#" class="nav-link"
                    :class="{ 'active': this.$route.name == 'product_list' || this.$route.name == 'product_add' }">
                    <i class="fas fa-list"></i>
                    Products
                  </a></router-link>
              </li> -->
            </ul>
          </li>

          <li class="nav-item">
            <router-link to="/manage_session" class="collapse-item"><a href="#" class="nav-link" id="manage_session"
                :class="{ 'active': this.$route.name == 'manage_session' }">
                <i class="fas fa-house-user"></i>
                Sessions
              </a> </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/userslist" class="collapse-item"><a href="#" class="nav-link" id="usermenu"
                :class="{ 'active': this.$route.name == 'userslist' || this.$route.name == 'usersadd' }">
                <i class="fas fa-users"></i>
                Users
              </a> </router-link>
          </li>

          <li class="nav-item" :class="{ 'menu-open': this.$route.name == 'census_doctor' || this.$route.name == 'census_patient' }">
            <a href="#" class="nav-link"
              :class="{ 'active': this.$route.name == 'census_doctor' || this.$route.name == 'census_patient' }">
              <i class="fas fa-poll-h"></i>
              <p>
                Census
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <router-link to="/census_doctor" class="collapse-item"> <a href="#" class="nav-link" id="usermenu"
                    :class="{ 'active': this.$route.name == 'census_doctor' }">
                    <i class="fas fa-user-md"></i>
                    Doctor
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/census_patient" class="collapse-item"> <a href="#" class="nav-link" id="usermenu"
                    :class="{ 'active': this.$route.name == 'census_patient' }">
                    <i class="fas fa-user-injured"></i>
                    Patient
                  </a></router-link>
              </li>

            </ul>
          </li>

          
          <li class="nav-item" :class="{ 'menu-open': this.$route.name == 'phic_list' || this.$route.name == 'copay_list' || this.$route.name == 'acpn_report' || this.$route.name == 'acpn_list' }">
            <a href="#" class="nav-link"
              :class="{ 'active': this.$route.name == 'census_doctor' || this.$route.name == 'census_patient' }">
              <i class="fas fa-poll-h"></i>
              <p>
                Reports
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <router-link to="/copay_list" class="collapse-item"> <a href="#" class="nav-link" id="usermenu"
                    :class="{ 'active': this.$route.name == 'copay_list' }">
                    <i class="far fa-circle nav-icon"></i>
                    Co-Pay
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/phic_list" class="collapse-item"> <a href="#" class="nav-link" id="usermenu"
                    :class="{ 'active': this.$route.name == 'phic_list' }">
                    <i class="far fa-circle nav-icon"></i>
                    PHIC
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/acpn_report" class="collapse-item"> <a href="#" class="nav-link" id="usermenu"
                    :class="{ 'active': this.$route.name == 'acpn_report' }">
                    <i class="far fa-circle nav-icon"></i>
                    PER SET ACPN
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/acpn_list" class="collapse-item"> <a href="#" class="nav-link" id="usermenu"
                    :class="{ 'active': this.$route.name == 'acpn_list' }">
                    <i class="far fa-circle nav-icon"></i>
                    ACPN PER BATCH
                  </a></router-link>
              </li>

            </ul>
          </li>

          
          <li class="nav-item">
            <router-link to="/revenue_list" class="collapse-item"><a href="#" class="nav-link" id="revenue_list"
                :class="{ 'active': this.$route.name == 'revenue_list' }">
                <i class="fas fa-share"></i>
                Revenue Sharing
              </a> </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/logs" ><a href="#" class="nav-link" id="revenuemenu"
                :class="{ 'active': this.$route.name == 'logs' }">
                <i class="fas fa-pen"></i>
                Logs
              </a> </router-link>
          </li>


          <li class="nav-item"
            :class="{ 'menu-open': this.$route.name == 'transaction_list' || this.$route.name == 'stocks' || this.$route.name == 'payment'  || this.$route.name == 'transaction' || this.$route.name == 'sales' || this.$route.name == 'ledger' || this.$route.name == 'product_add' || this.$route.name == 'rproduct_add' || this.$route.name == 'rproduct_list' || this.$route.name == 'product_list' }">
            <a href="#" class="nav-link"
              :class="{ 'active': this.$route.name == 'transaction_list' || this.$route.name == 'stocks'  || this.$route.name == 'payment'  || this.$route.name == 'transaction' || this.$route.name == 'sales'  || this.$route.name == 'ledger' || this.$route.name == 'rproduct_list' || this.$route.name == 'product_list' || this.$route.name == 'rproduct_add'}">
              <i class="nav-icon fas fa-folder-plus"></i>
              <p>
                Inventory
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <router-link to="/transaction_list"> <a href="#" class="nav-link"
                    :class="{ 'active': this.$route.name == 'transaction_list' || this.$route.name == 'transaction' }">
                    <i class="fas fa-cash-register"></i>
                    Transactions
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/product_list"> <a href="#" class="nav-link"
                    :class="{ 'active': this.$route.name == 'product_list' || this.$route.name == 'product_add' }">
                    <i class="fab fa-product-hunt"></i>
                    Products
                  </a></router-link>
              </li>
              <li class="nav-item">
                <!-- <router-link to="/rproduct_list"><a href="#" class="nav-link" -->
                <router-link to="/rproduct_add/0"><a href="#" class="nav-link"
                    :class="{ 'active': this.$route.name == 'rproduct_list' || this.$route.name == 'rproduct_add' }">
                    <i class="fas fa-hands"></i>
                    Received
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/stocks" class="collapse-item"> <a href="#" class="nav-link" id="usermenu"
                    :class="{ 'active': this.$route.name == 'stocks' }">
                    <i class="fas fa-socks"></i>
                    Stocks
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/ledger" class="collapse-item"> <a href="#" class="nav-link" id="ledger"
                    :class="{ 'active': this.$route.name == 'ledger' }">
                    <i class="fas fa-balance-scale"></i>
                    Ledger
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/payment" class="collapse-item"> <a href="#" class="nav-link" id="payment"
                    :class="{ 'active': this.$route.name == 'payment' }">
                    <i class="fas fa-shopping-cart"></i>
                    Payment
                  </a></router-link>
              </li>
              <li class="nav-item">
                <router-link to="/sales" class="collapse-item"> <a href="#" class="nav-link" id="sales"
                    :class="{ 'active': this.$route.name == 'sales' }">
                    <i class="fas fa-money-bill-alt"></i>
                    Sales
                  </a></router-link>
              </li>
              <!-- <li class="nav-item"
                :class="{ 'menu-open': this.$route.name == 'reports' || this.$route.name == 'yearly_report' || this.$route.name == 'transaction_report' }">
                <a href="#" class="nav-link">
                  <i class="fas fa-table"></i>
                  <p>
                    Reports
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <router-link to="/transaction_report" class=""> <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"
                          :class="{ 'fa-dot-circle': this.$route.name == 'transaction_report' }"></i>
                        Daily
                      </a></router-link>
                  </li>
                  <li class="nav-item">
                    <router-link to="/reports" class=""> <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon" :class="{ 'fa-dot-circle': this.$route.name == 'reports' }"></i>
                        Monthly
                      </a></router-link>
                  </li>
                  <li class="nav-item">
                    <router-link to="/yearly_report" class=""><a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"
                          :class="{ 'fa-dot-circle': this.$route.name == 'yearly_report' }"></i>
                        Yearly
                      </a></router-link>
                  </li>
                </ul>
              </li> -->
            </ul>
          </li>

          <!-- <li class="nav-item">
              <router-link to="/stocks" class="collapse-item"> <a href="#" class="nav-link" id="usermenu" :class="{'active':this.$route.name=='stocks'}">
                <i class="far fa-circle nav-icon"></i>
                Stocks                
              </a></router-link>
            </li> -->

          <li class="nav-item">
            <router-link to="/logout" class="nav-link">
              <p>Logout</p>
            </router-link>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
</template>
<script>
export default {
  data() {
    return {
      img: '../../../../backend2/premier.jpg',
      avatar: '../../../../backend2/dist/img/avatar3.png',
    }
  },
}
</script>
  